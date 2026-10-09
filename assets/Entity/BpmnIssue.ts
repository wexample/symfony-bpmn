import AbstractApiEntity from '@wexample/js-api-entity/Common/AbstractApiEntity';
import schema from '../data/entity/bpmn_issue.json';

export default class BpmnIssue extends AbstractApiEntity {
  static readonly entityName = 'bpmnIssue';

  static retrieveEntitySchema() {
    return schema;
  }
}
