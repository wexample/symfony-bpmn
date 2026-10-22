import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/bpmn_path.json';

export default class BpmnPath extends AbstractApiEntity {
  static readonly entityName = 'bpmnPath';

  static retrieveEntitySchema() {
    return schema;
  }
}
